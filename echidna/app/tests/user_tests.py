import unittest
import app
from app.models.user import User


class UserTestCasee(unittest.TestCase):

    def setUp(self):
        pass

    def tearDown(self):
        pass

    def test__is_user_email_correct(self):
        user = User('noreply@noreply.no', 'ABCabc123!@#', 'John Doe')
        self.assertEqual(user._is_user_email_correct('noreply.no'), False)
        self.assertEqual(user._is_user_email_correct('noreply@noreply.no'), True)


if __name__ == '__main__':
    unittest.main()
